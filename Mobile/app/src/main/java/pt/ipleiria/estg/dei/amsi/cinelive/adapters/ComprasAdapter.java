package pt.ipleiria.estg.dei.amsi.cinelive.adapters;

import android.view.LayoutInflater;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ItemCompraBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Compra;

public class ComprasAdapter extends RecyclerView.Adapter<ComprasAdapter.ViewHolder> {
    private final List<Compra> compras;
    private final OnCompraClickListener listener;

    public interface OnCompraClickListener {
        void onCompraSelected(Compra compra);
    }

    public ComprasAdapter(List<Compra> compras, OnCompraClickListener listener) {
        this.compras = compras;
        this.listener = listener;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {

        // Inflate do layout de um item (item_compra.xml)
        ItemCompraBinding binding = ItemCompraBinding.inflate(
                LayoutInflater.from(parent.getContext()), parent, false
        );
        return new ViewHolder(binding);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        Compra compra = compras.get(position);

        // Preencher campos do layout com os dados da compra
        holder.binding.tvTituloFilme.setText(compra.getTituloFilme());
        holder.binding.tvDataCompra.setText(compra.getData());
        holder.binding.tvNomeCinema.setText(compra.getNomeCinema());
        holder.binding.tvTotal.setText(compra.getTotal());
        holder.binding.tvDataSessao.setText(compra.getDataSessao());
        holder.binding.tvHoraInicioSessao.setText(compra.getHoraInicioSessao());
        holder.binding.tvLugares.setText(compra.getLugares());

        // Botão foi clicado --> avisar o fragment
        holder.binding.btnDetalhes.setOnClickListener(v -> {
            if (listener != null) {
                listener.onCompraSelected(compra);
            }
        });
    }

    @Override
    public int getItemCount() {
        return compras.size();
    }

    // O ViewHolder representa 1 item da lista
    static class ViewHolder extends RecyclerView.ViewHolder {
        ItemCompraBinding binding;

        public ViewHolder(ItemCompraBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }
}